<?php
?>
<section class="card">
    <h2 class="page-title">Add Lead</h2>
    <form class="form" action="" method="POST">
        <div class="row">
            <div>
                <label class="label" for="name">Full name</label>
                <input class="input" id="name" name="name" placeholder="eg. Sam Smith" required/>
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" name="email" type="email" placeholder="eg. sam@example.com" required/>
            </div>
        </div>
        <div class="row">
            <div>
                <label class="label" for="phone">Phone</label>
                <input class="input" id="phone" name="phone" placeholder="eg. +1 555 123 4567" required/>
            </div>
            <div>
                <label class="label" for="budget">Budget (USD)</label>
                <input class="input" id="budget" name="budget" type="number" step="0.1" placeholder="eg. 4200" required/>
            </div>
        </div>
        <div>
            <label class="label" for="message">Message</label>
            <textarea class="textarea" id="message" name="message" placeholder="Describe your request..." required></textarea>
            
        </div>
        <button class="btn" type="submit">Submit</button>
    </form>
 </section>

